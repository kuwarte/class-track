import { useEffect, useState } from "react";

const API_BASE = "http://localhost:8000/api/v1";

interface Subject {
	id: number;
	name: string;
}

interface Entry {
	id: number;
	subject_id: number;
	topic: string;
	notes: string;
	date: string;
}

export default function App() {
	const [subjects, setSubjects] = useState<Subject[]>([]);
	const [selectedSubject, setSelectedSubject] = useState<Subject | null>(null);
	const [newSubjectName, setNewSubjectName] = useState("");
	const [entries, setEntries] = useState<Entry[]>([]);
	const [newTopic, setNewTopic] = useState("");
	const [newNotes, setNewNotes] = useState("");

	const loadSubjects = async () => {
		const res = await fetch(`${API_BASE}/subjects`);
		const data = await res.json();
		setSubjects(data);
	};

	const addSubject = async () => {
		if (!newSubjectName) return;
		await fetch(`${API_BASE}/subjects`, {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ name: newSubjectName }),
		});
		setNewSubjectName("");
		loadSubjects();
	};

	const deleteSubject = async (id: number) => {
		await fetch(`${API_BASE}/subjects`, {
			method: "DELETE",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ id }),
		});
		if (selectedSubject?.id === id) setSelectedSubject(null);
		loadSubjects();
	};

	const loadEntries = async (subject_id: number) => {
		const res = await fetch(`${API_BASE}/entries?subject_id=${subject_id}`);
		const data = await res.json();
		setEntries(data);
	};

	const addEntry = async () => {
		if (!selectedSubject || !newTopic) return;
		await fetch(`${API_BASE}/entries`, {
			method: "POST",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({
				subject_id: selectedSubject.id,
				topic: newTopic,
				notes: newNotes,
			}),
		});
		setNewTopic("");
		setNewNotes("");
		loadEntries(selectedSubject.id);
	};

	const deleteEntry = async (id: number) => {
		await fetch(`${API_BASE}/entries`, {
			method: "DELETE",
			headers: { "Content-Type": "application/json" },
			body: JSON.stringify({ id }),
		});
		if (selectedSubject) loadEntries(selectedSubject.id);
	};

	useEffect(() => {
		loadSubjects();
	}, []);

	useEffect(() => {
		if (selectedSubject) loadEntries(selectedSubject.id);
	}, [selectedSubject]);

	return (
		<div
			style={{
				display: "flex",
				flexDirection: "column",
				padding: 50,
				fontFamily: "sans-serif",
				backgroundColor: "#f0f0f0",
				minHeight: "70vh",
				gap: 20,
			}}
		>
			<div style={{ display: "flex", flexDirection: "column", gap: 10 }}>
				<h2 style={{ margin: 0 }}>Subjects</h2>
				<div style={{ display: "flex", gap: 5 }}>
					<input
						type="text"
						placeholder="New Subject"
						value={newSubjectName}
						onChange={(e) => setNewSubjectName(e.target.value)}
						style={{ flex: 1, padding: 4 }}
					/>
					<button onClick={addSubject} style={{ padding: "4px 8px" }}>
						Add
					</button>
				</div>
				<ul style={{ listStyle: "none", padding: 0, margin: 0 }}>
					{subjects.map((s) => (
						<li
							key={s.id}
							style={{
								display: "flex",
								justifyContent: "space-between",
								padding: "8px 8px",
								alignItems: "center",
								cursor: "pointer",
								background: "white",
								marginTop: 4,
								border: "1px solid #999",
							}}
						>
							<span onClick={() => setSelectedSubject(s)}>{s.name}</span>
							<button
								onClick={() => deleteSubject(s.id)}
								style={{
									padding: "4px 6px",
									border: "none",
									cursor: "pointer",
								}}
							>
								✕
							</button>
						</li>
					))}
				</ul>
			</div>

			<div
				style={{
					display: "flex",
					flexDirection: "column",
					gap: 10,
					border: "1px solid #777",
					padding: 20,
					minHeight: 400,
					backgroundColor: "#ccc",
				}}
			>
				{selectedSubject ? (
					<>
						<h2 style={{ margin: 0 }}>Entries for {selectedSubject.name}</h2>
						<div style={{ display: "flex", gap: 5 }}>
							<input
								type="text"
								placeholder="Topic"
								value={newTopic}
								onChange={(e) => setNewTopic(e.target.value)}
								style={{ flex: 1, padding: 4 }}
							/>
							<input
								type="text"
								placeholder="Notes"
								value={newNotes}
								onChange={(e) => setNewNotes(e.target.value)}
								style={{ flex: 1, padding: 4 }}
							/>
							<button
								onClick={addEntry}
								style={{ padding: "4px 8px", cursor: "pointer" }}
							>
								Add
							</button>
						</div>
						<ul style={{ listStyle: "none", padding: 0, margin: 0 }}>
							{entries.map((e) => (
								<li
									key={e.id}
									style={{
										display: "flex",
										justifyContent: "space-between",
										alignItems: "center",
										padding: "8px 8px",
										cursor: "pointer",
										background: "white",
										fontSize: 14,
										overflow: "hidden",
										whiteSpace: "nowrap",
										textOverflow: "ellipsis",
										marginTop: 4,
									}}
								>
									<span style={{ flex: 1, marginRight: 10 }}>
										<strong>{e.topic}</strong> — {e.notes}
									</span>
									<span style={{ marginRight: 12 }}>{e.date}</span>
									<button
										onClick={() => deleteEntry(e.id)}
										style={{
											padding: "4px 6px",
											border: "none",
											cursor: "pointer",
										}}
									>
										✕
									</button>
								</li>
							))}
						</ul>
					</>
				) : (
					<p>SELECT A SUBJECT TO SEE ENTRIES</p>
				)}
			</div>
		</div>
	);
}
